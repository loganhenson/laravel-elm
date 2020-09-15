module Auth.Register.Main exposing (..)

import Dict exposing (Dict)
import Html exposing (Html, button, div, form, input, label, text)
import Html.Attributes exposing (attribute, class, classList, for, id, type_, value)
import Html.Events exposing (onInput, onSubmit)
import Json.Decode exposing (Decoder, Error, Value, decodeValue, dict, list, string)
import Json.Decode.Pipeline exposing (required)
import Json.Encode
import LaravelElm exposing (Errors, page, receiveNewProps)
import Maybe exposing (withDefault)
import Routes exposing (post)


type alias Props =
    { errors : Errors }


type alias State =
    { name : String
    , email : String
    , password : String
    , password_confirmation : String
    }


type alias Model =
    { props : Props
    , state : State
    }


type Msg
    = NewProps Value
    | Submit
    | SetName String
    | SetEmail String
    | SetPassword String
    | SetPasswordConfirmation String


decodeProps : Decoder Props
decodeProps =
    Json.Decode.succeed Props
        |> required "errors" (dict (list string))


stateFromProps : Props -> State
stateFromProps =
    \_ -> { name = "", email = "", password = "", password_confirmation = "" }


main : Program Value (Result Error Model) Msg
main =
    LaravelElm.page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , view = view
        , update = update
        , subscriptions = \_ -> LaravelElm.receiveNewProps NewProps
        , onMount = \_ -> Cmd.none
        }


update : Msg -> Model -> ( Model, Cmd Msg )
update msg { props, state } =
    case msg of
        NewProps newProps ->
            ( { props = Result.withDefault props <| decodeValue decodeProps newProps
              , state = state
              }
            , Cmd.none
            )

        SetName newName ->
            ( { props = props, state = { state | name = newName } }, Cmd.none )

        SetEmail newEmail ->
            ( { props = props, state = { state | email = newEmail } }, Cmd.none )

        SetPassword newPassword ->
            ( { props = props, state = { state | password = newPassword } }, Cmd.none )

        SetPasswordConfirmation newPasswordConfirmation ->
            ( { props = props, state = { state | password_confirmation = newPasswordConfirmation } }, Cmd.none )

        Submit ->
            ( { props = props, state = state }
            , post <|
                Json.Encode.object
                    [ ( "url", Json.Encode.string Routes.register )
                    , ( "data"
                      , Json.Encode.object
                            [ ( "name", Json.Encode.string state.name )
                            , ( "email", Json.Encode.string state.email )
                            , ( "password", Json.Encode.string state.password )
                            , ( "password_confirmation", Json.Encode.string state.password_confirmation )
                            ]
                      )
                    ]
            )


view : Model -> Html Msg
view { props, state } =
    div [ class "container mx-auto m-4 p-4" ]
        [ div [ class "flex flex-wrap justify-center" ]
            [ div [ class "w-full max-w-sm" ]
                [ div [ class "flex flex-col break-words bg-white border border-2 rounded shadow-md" ]
                    [ div [ class "font-semibold bg-indigo-500 text-white py-3 px-6 mb-0" ]
                        [ text "Register" ]
                    , form [ onSubmit <| Submit, class "w-full p-6" ]
                        [ div [ class "flex flex-wrap mb-6" ]
                            [ label [ class "block text-gray-700 text-sm font-bold mb-2", for "email" ]
                                [ text "Name" ]
                            , input [ onInput SetName, value state.name, attribute "autofocus" "", class "shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline", classList [ ( "border-red-500", Dict.get "name" props.errors |> Maybe.withDefault [] |> List.isEmpty |> not ) ], id "name", attribute "required" "", attribute "autocomplete" "name" ] []
                            , div [ class "text-red-500 text-xs italic mt-4" ]
                                (List.map (\error -> div [] [ text error ]) (Dict.get "name" props.errors |> withDefault []))
                            ]
                        , div [ class "flex flex-wrap mb-6" ]
                            [ label [ class "block text-gray-700 text-sm font-bold mb-2", for "email" ]
                                [ text "E-Mail Address" ]
                            , input [ onInput SetEmail, value state.email, class "shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline", classList [ ( "border-red-500", Dict.get "email" props.errors |> Maybe.withDefault [] |> List.isEmpty |> not ) ], id "email", attribute "required" "", type_ "email", attribute "autocomplete" "email" ] []
                            , div [ class "text-red-500 text-xs italic mt-4" ]
                                (List.map (\error -> div [] [ text error ]) (Dict.get "email" props.errors |> withDefault []))
                            ]
                        , div [ class "flex flex-wrap mb-6" ]
                            [ label [ class "block text-gray-700 text-sm font-bold mb-2", for "password" ]
                                [ text "Password" ]
                            , input [ onInput SetPassword, value state.password, class "shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline", classList [ ( "border-red-500", Dict.get "password" props.errors |> Maybe.withDefault [] |> List.isEmpty |> not ) ], id "password", attribute "required" "", type_ "password", attribute "autocomplete" "current-password" ] []
                            , div [ class "text-red-500 text-xs italic mt-4" ]
                                (List.map (\error -> div [] [ text error ]) (Dict.get "password" props.errors |> withDefault []))
                            ]
                        , div [ class "flex flex-wrap mb-6" ]
                            [ label [ class "block text-gray-700 text-sm font-bold mb-2", for "password" ]
                                [ text "Confirm Password" ]
                            , input [ onInput SetPasswordConfirmation, value state.password_confirmation, class "shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline", id "password_confirmation", attribute "required" "", type_ "password" ] []
                            ]
                        , div [ class "flex flex-wrap items-center" ]
                            [ button [ class "inline-block align-middle text-center select-none border font-bold whitespace-no-wrap py-2 px-4 rounded text-base leading-normal no-underline text-gray-100 bg-indigo-500 hover:bg-indigo-700", type_ "submit" ]
                                [ text "Register" ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
