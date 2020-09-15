module Auth.Email.Main exposing (..)

import Dict exposing (Dict)
import Html exposing (Html, a, button, div, form, input, label, p, text)
import Html.Attributes exposing (attribute, class, classList, for, id, type_, value)
import Html.Events exposing (onClick, onInput, onSubmit)
import Json.Decode exposing (Decoder, Error, Value, decodeValue, dict, list, nullable, string, succeed)
import Json.Decode.Pipeline exposing (required)
import Json.Encode
import LaravelElm exposing (Errors, page, receiveNewProps)
import Maybe exposing (withDefault)
import Routes exposing (get, post)


type alias Props =
    { errors : Errors
    , status : Maybe String
    }


type alias State =
    { email : String }


type alias Model =
    { props : Props
    , state : State
    }


type Msg
    = NewProps Value
    | Submit
    | SetEmail String
    | BackToLogin


decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "errors" (dict (list string))
        |> required "status" (nullable string)


stateFromProps : Props -> State
stateFromProps props =
    { email = "" }


main : Program Value (Result Error Model) Msg
main =
    LaravelElm.page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , update = update
        , view = view
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

        BackToLogin ->
            ( { props = props, state = state }
            , get Routes.login
            )

        SetEmail email ->
            ( { props = props, state = { state | email = email } }
            , Cmd.none
            )

        Submit ->
            ( { props = props, state = state }
            , post <|
                Json.Encode.object
                    [ ( "url", Json.Encode.string Routes.passwordEmail )
                    , ( "data"
                      , Json.Encode.object
                            [ ( "email", Json.Encode.string state.email )
                            ]
                      )
                    ]
            )


view : Model -> Html Msg
view { props, state } =
    div [ class "container mx-auto m-4 p-4" ]
        [ div [ class "flex flex-wrap justify-center" ]
            [ div [ class "w-full max-w-sm" ]
                [ case props.status of
                    Just status ->
                        div [ class "text-sm border border-t-8 rounded text-green-700 border-green-600 bg-green-100 px-3 py-4 mb-4" ]
                            [ text status
                            ]

                    Nothing ->
                        text ""
                , div [ class "flex flex-col break-words bg-white border border-2 rounded shadow-md" ]
                    [ div [ class "font-semibold bg-gray-200 text-gray-700 py-3 px-6 mb-0" ]
                        [ text "Reset Password" ]
                    , form [ onSubmit <| Submit, class "w-full p-6" ]
                        [ div [ class "flex flex-wrap mb-6" ]
                            [ label [ class "block text-gray-700 text-sm font-bold mb-2", for "email" ]
                                [ text "E-Mail Address:" ]
                            , input [ onInput SetEmail, value state.email, attribute "autofocus" "", class "shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker leading-tight focus:outline-none focus:shadow-outline", classList [ ( "border-red-500", Dict.get "email" props.errors |> Maybe.withDefault [] |> List.isEmpty |> not ) ], id "email", attribute "required" "", type_ "email" ]
                                []
                            , div [ class "text-red-500 text-xs italic mt-4" ]
                                (List.map (\error -> div [] [ text error ]) (Dict.get "email" props.errors |> withDefault []))
                            ]
                        , div [ class "flex flex-wrap" ]
                            [ button [ class "bg-indigo-500 hover:bg-indigo-700 text-gray-100 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline", type_ "submit" ]
                                [ text "Send Password Reset Link" ]
                            , p [ class "w-full text-xs text-center text-grey-dark mt-8 -mb-4" ]
                                [ a [ onClick BackToLogin, class "text-indigo-500 hover:text-indigo-700 no-underline cursor-pointer" ]
                                    [ text "Back to login" ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
