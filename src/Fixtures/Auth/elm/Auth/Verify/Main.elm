module Auth.Verify.Main exposing (..)

import Html exposing (Html, button, div, form, text)
import Html.Attributes exposing (class, type_)
import Html.Events exposing (onSubmit)
import Json.Decode exposing (Decoder, Error, Value, bool, decodeValue, dict, list, nullable, string, succeed)
import Json.Decode.Pipeline exposing (required)
import Json.Encode
import LaravelElm exposing (Errors, page, receiveNewProps)
import Routes exposing (post)


type alias Props =
    { errors : Errors
    , status : Maybe Bool
    }


type alias State =
    {}


type alias Model =
    { props : Props
    , state : State
    }


type Msg
    = NewProps Value
    | Submit


decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "errors" (dict (list string))
        |> required "status" (nullable bool)


stateFromProps : Props -> State
stateFromProps props =
    {}


main : Program Value (Result Error Model) Msg
main =
    page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , update = update
        , view = view
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

        Submit ->
            ( { props = props, state = state }
            , post <|
                Json.Encode.object
                    [ ( "url", Json.Encode.string Routes.verificationResend )
                    ]
            )


view : Model -> Html Msg
view { props, state } =
    div [ class "container mx-auto m-4 p-4" ]
        [ div [ class "flex flex-wrap justify-center" ]
            [ div [ class "w-full max-w-sm" ]
                [ div [ class "flex flex-col break-words bg-white border border-2 rounded shadow-md" ]
                    [ div [ class "font-semibold bg-gray-200 text-gray-700 py-3 px-6 mb-0" ]
                        [ text "Verify Your Email Address" ]
                    , form [ onSubmit <| Submit, class "w-full p-6" ]
                        [ div [ class "flex flex-wrap" ]
                            [ text "Please check your email for a verification link."
                            , button [ class "focus:outline-none my-4 text-left p-0 m-0 align-baseline text-blue-500 hover:text-blue-700", type_ "submit" ]
                                [ text "Click here to resend the email" ]
                            , div []
                                [ case props.status of
                                    Just status ->
                                        case status of
                                            True ->
                                                text "A fresh verification link has been sent to your email address."

                                            False ->
                                                text "Something went wrong."

                                    Nothing ->
                                        text ""
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
