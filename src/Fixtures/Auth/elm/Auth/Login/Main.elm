module Auth.Login.Main exposing (..)

import Dict exposing (Dict)
import Html exposing (Html, a, button, div, form, input, label, text)
import Html.Attributes exposing (attribute, class, classList, for, id, type_, value)
import Html.Events exposing (onClick, onInput, onSubmit)
import Json.Decode exposing (Decoder, Error, Value, decodeValue, dict, list, string, succeed)
import Json.Decode.Pipeline exposing (required)
import Json.Encode
import LaravelElm exposing (Errors, page, receiveNewProps)
import Maybe exposing (withDefault)
import Routes exposing (get, post)


type alias Props =
    { errors : Errors }


type alias State =
    { email : String
    , password : String
    }


type alias Model =
    { props : Props
    , state : State
    }


type Msg
    = NewProps Value
    | Submit
    | SetEmail String
    | SetPassword String
    | ForgotPassword


decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "errors" (dict (list string))


stateFromProps : Props -> State
stateFromProps =
    \_ -> { email = "", password = "" }


main : Program Value (Result Error Model) Msg
main =
    page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , view = view
        , update = update
        , subscriptions = \_ -> receiveNewProps NewProps
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

        ForgotPassword ->
            ( { props = props, state = state }, get Routes.passwordUpdate )

        SetEmail newEmail ->
            ( { props = props, state = { state | email = newEmail } }, Cmd.none )

        SetPassword newPassword ->
            ( { props = props, state = { state | password = newPassword } }, Cmd.none )

        Submit ->
            ( { props = props, state = state }
            , post <|
                Json.Encode.object
                    [ ( "url", Json.Encode.string Routes.login )
                    , ( "data"
                      , Json.Encode.object
                            [ ( "email", Json.Encode.string state.email )
                            , ( "password", Json.Encode.string state.password )
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
                        [ text "Login" ]
                    , form [ onSubmit <| Submit, class "w-full p-6" ]
                        [ div [ class "flex flex-wrap mb-6" ]
                            [ label [ class "block text-gray-700 text-sm font-bold mb-2", for "email" ]
                                [ text "E-Mail Address" ]
                            , input [ onInput SetEmail, value state.email, attribute "autofocus" "", class "shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline", classList [ ( "border-red-500", Dict.get "email" props.errors |> Maybe.withDefault [] |> List.isEmpty |> not ) ], id "email", attribute "required" "", type_ "email", attribute "autocomplete" "email" ] []
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
                        , div [ class "flex flex-wrap items-center" ]
                            [ button [ class "inline-block align-middle text-center select-none border font-bold whitespace-no-wrap py-2 px-4 rounded text-base leading-normal no-underline text-gray-100 bg-indigo-500 hover:bg-indigo-700", type_ "submit" ]
                                [ text "Login" ]
                            , a [ onClick ForgotPassword, class "text-sm text-indigo-500 hover:text-indigo-700 whitespace-no-wrap no-underline ml-auto cursor-pointer" ] [ text "Forgot Your Password?" ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
